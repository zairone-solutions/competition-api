import * as aws from "@pulumi/aws";
import * as pulumi from "@pulumi/pulumi";
import * as dotenv from "dotenv";

dotenv.config();

const defaultVpc = aws.ec2.getVpc({ default: true });
const defaultSubnets = defaultVpc.then(vpc =>
    aws.ec2.getSubnets({
        filters: [
            { name: "vpc-id", values: [vpc.id] },
        ],
    })
);
const base64PrivateKey = process.env.GITHUB_BASE64_PRIVATE_KEY;

const secGroup = new aws.ec2.SecurityGroup("uniquo-sec-group", {
    description: "Allow HTTP, HTTPS, and SSH",
    vpcId: defaultVpc.then(vpc => vpc.id),
    ingress: [
        { protocol: "tcp", fromPort: 80, toPort: 80, cidrBlocks: ["0.0.0.0/0"] },
        { protocol: "tcp", fromPort: 443, toPort: 443, cidrBlocks: ["0.0.0.0/0"] },
        { protocol: "tcp", fromPort: 22, toPort: 22, cidrBlocks: ["0.0.0.0/0"] },
    ],
    egress: [
        { protocol: "-1", fromPort: 0, toPort: 0, cidrBlocks: ["0.0.0.0/0"] },
    ],
});

const instance = new aws.ec2.Instance("uniquo-instance", {
    instanceType: "t3.xlarge",
    ami: process.env.AWS_AMI,
    keyName: process.env.AWS_KEY_PAIR,
    subnetId: defaultSubnets.then(subnets => subnets.ids[0]),
    vpcSecurityGroupIds: [secGroup.id],
    userData: pulumi.interpolate`#!/bin/bash
        # Update package list and install necessary packages
        sudo apt update
        sudo apt install -y docker.io docker-compose git

        # Create SSH directory and set permissions
        mkdir -p /home/ubuntu/.ssh
        chmod 700 /home/ubuntu/.ssh

        # Decode and store the private key for GitHub
        echo ${base64PrivateKey} | base64 --decode > /home/ubuntu/.ssh/github_personal
        chmod 600 /home/ubuntu/.ssh/github_personal

        # Create and configure SSH config file for GitHub
        touch /home/ubuntu/.ssh/config
        echo "Host github.com" >> /home/ubuntu/.ssh/config
        echo "  AddKeysToAgent yes" >> /home/ubuntu/.ssh/config
        echo "  IdentityFile ~/.ssh/github_personal" >> /home/ubuntu/.ssh/config
        chmod 600 /home/ubuntu/.ssh/config

        # Start the Docker containers
        git clone git@github.com:alinaqi2000/uniquo-server.git /home/ubuntu/uniquo-server
        cd /home/ubuntu/uniquo-server
        sudo docker-compose up --build -d
`,

    tags: { Name: "UniquoServer" },
});

export const publicIp = instance.publicIp;
export const publicDns = instance.publicDns;
